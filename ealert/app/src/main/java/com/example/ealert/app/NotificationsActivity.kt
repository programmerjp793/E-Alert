package com.example.ealert.app

import android.os.Bundle
import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.ealert.R
import com.example.ealert.app.response.Notification
import com.example.ealert.app.response.NotificationResponse
import com.example.ealert.app.retrofit.RetrofitClient
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class NotificationsActivity : AppCompatActivity() {
    private lateinit var recyclerView: RecyclerView
    private lateinit var progressBar: ProgressBar
    private lateinit var emptyText: TextView
    private lateinit var apiService: ApiService

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_notifications)

        // Initialize views
        recyclerView = findViewById(R.id.recyclerView)
        progressBar = findViewById(R.id.progressBar)
        emptyText = findViewById(R.id.emptyText)

        // Setup RecyclerView
        recyclerView.layoutManager = LinearLayoutManager(this)

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main)) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }

        // Get user ID from shared preferences
        val sharedPref = getSharedPreferences("user_prefs", MODE_PRIVATE)
        val userId = sharedPref.getInt("user_id", 0)

        if (userId == 0) {
            showError("User not logged in")
            return
        }

        // Initialize Retrofit
        apiService = RetrofitClient.instance

        // Fetch notifications for this user
        fetchUserNotifications(userId)
    }

    private fun fetchUserNotifications(userId: Int) {
        progressBar.visibility = View.VISIBLE
        emptyText.visibility = View.GONE

        apiService.getNotifications(userId).enqueue(object : Callback<NotificationResponse> {
            override fun onResponse(
                call: Call<NotificationResponse>,
                response: Response<NotificationResponse>
            ) {
                progressBar.visibility = View.GONE

                when {
                    !response.isSuccessful -> {
                        showError("API Error: ${response.code()}")
                    }
                    response.body()?.status != "success" -> {
                        showError(response.body()?.message ?: "Failed to load notifications")
                    }
                    response.body()?.data.isNullOrEmpty() -> {
                        showEmptyState("No notifications available")
                    }
                    else -> {
                        response.body()?.let { notificationResponse ->
                            notificationResponse.data?.let { notifications ->
                                // Filter out duplicates by response date
                                val uniqueNotifications = notifications.distinctBy { it.responseDate }

                                if (uniqueNotifications.isEmpty()) {
                                    showEmptyState("No notifications available")
                                } else {
                                    recyclerView.adapter = NotificationAdapter(uniqueNotifications)
                                    recyclerView.visibility = View.VISIBLE
                                    Toast.makeText(
                                        this@NotificationsActivity,
                                        "Loaded ${uniqueNotifications.size} notifications",
                                        Toast.LENGTH_SHORT
                                    ).show()
                                }
                            }
                        }
                    }
                }
            }

            override fun onFailure(call: Call<NotificationResponse>, t: Throwable) {
                progressBar.visibility = View.GONE
                showError("Network error: ${t.message}")
                Log.e("NotificationsActivity", "Network request failed", t)
            }
        })
    }

    private fun showEmptyState(message: String) {
        runOnUiThread {
            emptyText.text = message
            recyclerView.visibility = View.GONE
            emptyText.visibility = View.VISIBLE
        }
    }

    private fun showError(message: String) {
        runOnUiThread {
            Toast.makeText(this, message, Toast.LENGTH_LONG).show()
            showEmptyState(message)
        }
    }
}

class NotificationAdapter(private val notifications: List<Notification>) :
    RecyclerView.Adapter<NotificationAdapter.ViewHolder>() {

    class ViewHolder(view: View) : RecyclerView.ViewHolder(view) {
        val emergencyTypeText: TextView = view.findViewById(R.id.emergencyTypeText)
        val subjectReportText: TextView = view.findViewById(R.id.subjectReportText)
        val statusText: TextView = view.findViewById(R.id.statusText)
        val reportResponseText: TextView = view.findViewById(R.id.reportResponseText)
        val responderUsernameText: TextView = view.findViewById(R.id.responderUsernameText)
        val responseDateText: TextView = view.findViewById(R.id.responseDateText)
        val agenciesText: TextView = view.findViewById(R.id.agenciesText)

    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_notification, parent, false)
        return ViewHolder(view)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val notification = notifications[position]
        holder.emergencyTypeText.text = "Emergency: ${notification.emergencyType}"
        holder.subjectReportText.text = "Subject: ${notification.subjectReport}"
        holder.statusText.text = "Status: ${notification.status}"
        holder.responderUsernameText.text = "Responder: ${notification.responderUsername}"
        holder.reportResponseText.text = "Responses: ${notification.responses ?: ""}"
        holder.responseDateText.text = "Date: ${notification.responseDate}"
        holder.agenciesText.text = "Agencies: ${notification.emergencyAgencies}"

    }

    override fun getItemCount() = notifications.size
}
